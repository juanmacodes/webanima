#pragma once

#include "CoreMinimal.h"
#include "GameFramework/Actor.h"
#include "RoadGenerator.generated.h"

class ARoadNode;
class ARoadSegment;
class ARoadNetworkSubsystem;

UCLASS()
class ARoadGenerator : public AActor
{
    GENERATED_BODY()

public:
    ARoadGenerator();

    UFUNCTION(BlueprintCallable, Category="Road Generation")
    void GenerateNetwork();

    UFUNCTION(BlueprintCallable, Category="Road Generation")
    void ConnectPlacedNodes();

    UFUNCTION(BlueprintCallable, Category="Road Generation")
    ARoadSegment* SpawnRoadBetween(ARoadNode* A, ARoadNode* B);

    UFUNCTION(BlueprintCallable, Category="Road Generation")
    void ClearGeneratedNetwork();

    void SetSeed(int32 InSeed);
    void SetNetworkActor(ARoadNetworkSubsystem* InNetwork);

protected:
    virtual void BeginPlay() override;

private:
    bool IsValidNodeLocation(const FVector& Location, const TArray<ARoadNode*>& Nodes) const;
    void DestroyGeneratedActors();

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category="Road Generation", meta=(AllowPrivateAccess="true"))
    int32 Seed;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category="Road Generation", meta=(ClampMin="0.0", AllowPrivateAccess="true"))
    float MinIntersectionSpacing;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category="Road Generation", meta=(ClampMin="0", AllowPrivateAccess="true"))
    int32 MaxRoads;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category="Road Generation", meta=(ClampMin="0.0", AllowPrivateAccess="true"))
    float GenerationRadius;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category="Road Generation", meta=(AllowPrivateAccess="true"))
    TSubclassOf<ARoadNode> RoadNodeClass;

    UPROPERTY(EditAnywhere, BlueprintReadWrite, Category="Road Generation", meta=(AllowPrivateAccess="true"))
    TSubclassOf<ARoadSegment> RoadSegmentClass;

    UPROPERTY(Transient)
    TArray<TWeakObjectPtr<ARoadNode>> GeneratedNodes;

    UPROPERTY(Transient)
    TArray<TWeakObjectPtr<ARoadSegment>> GeneratedSegments;

    TWeakObjectPtr<ARoadNetworkSubsystem> NetworkActor;
};
