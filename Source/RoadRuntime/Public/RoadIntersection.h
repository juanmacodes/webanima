#pragma once

#include "CoreMinimal.h"
#include "GameFramework/Actor.h"
#include "RoadTypes.h"
#include "RoadIntersection.generated.h"

class ARoadSegment;

UCLASS()
class ARoadIntersection : public AActor
{
    GENERATED_BODY()

public:
    ARoadIntersection();

    UPROPERTY(EditAnywhere, Category="Intersection")
    EIntersectionType IntersectionType;

    UPROPERTY(EditAnywhere, Category="Intersection")
    int32 NumRoads;

    UPROPERTY(EditAnywhere, Category="Intersection")
    TArray<FRoadConnection> ConnectedRoads;

    UFUNCTION(BlueprintCallable, Category="Intersection")
    void AddRoad(ARoadSegment* Road, int32 ConnectionIndex = -1);

    UFUNCTION(BlueprintCallable, Category="Intersection")
    void GenerateIntersectionGeometry();

    UFUNCTION(BlueprintCallable, Category="Intersection")
    void ConfigureTrafficRules();

private:
    void UpdateConnection(int32 Index, ARoadSegment* Road);
    FVector ComputeIntersectionCenter() const;

    UPROPERTY(Transient)
    TMap<TWeakObjectPtr<ARoadSegment>, TArray<TWeakObjectPtr<ARoadSegment>>> TrafficRouting;
};
