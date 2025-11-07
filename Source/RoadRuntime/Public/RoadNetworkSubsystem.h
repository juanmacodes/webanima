#pragma once

#include "CoreMinimal.h"
#include "GameFramework/Actor.h"
#include "RoadNetworkSubsystem.generated.h"

class ARoadNode;
class ARoadSegment;
class ARoadIntersection;
class ARoadGenerator;

UCLASS()
class ARoadNetworkSubsystem : public AActor
{
    GENERATED_BODY()

public:
    ARoadNetworkSubsystem();

    UFUNCTION(BlueprintCallable, Category="Road Network")
    void RegisterRoadNode(ARoadNode* Node);

    UFUNCTION(BlueprintCallable, Category="Road Network")
    void RegisterRoadSegment(ARoadSegment* Segment);

    UFUNCTION(BlueprintCallable, Category="Road Network")
    void RegisterIntersection(ARoadIntersection* Intersection);

    UFUNCTION(BlueprintCallable, Category="Road Network")
    void GenerateRoadNetwork(int32 Seed);

    UFUNCTION(BlueprintCallable, Category="Road Network")
    void ClearRoadNetwork();

    UFUNCTION(BlueprintCallable, Category="Road Network")
    TArray<ARoadSegment*> FindRouteBetween(ARoadNode* A, ARoadNode* B);

protected:
    virtual void BeginDestroy() override;

private:
    void CleanupInvalidEntries();
    void UpdateTopology();
    void RegisterSegmentConnections(ARoadSegment* Segment);

    UPROPERTY(EditAnywhere, Category="Road Network")
    TSubclassOf<ARoadGenerator> GeneratorClass;

    UPROPERTY(Transient)
    ARoadGenerator* ActiveGenerator;

    UPROPERTY(Transient)
    TArray<TWeakObjectPtr<ARoadNode>> RoadNodes;

    UPROPERTY(Transient)
    TArray<TWeakObjectPtr<ARoadSegment>> RoadSegments;

    UPROPERTY(Transient)
    TArray<TWeakObjectPtr<ARoadIntersection>> RoadIntersections;

    TMap<ARoadNode*, TArray<TWeakObjectPtr<ARoadSegment>>> NodeSegments;
};
